'''
This class models a node in a tree of nodes
representing a node in a file system directory tree.
Each node has a name and a size in bytes. The name 
represents the name of the node in the directory/filename
path.

There are class functions, as opposed to member functions, that
enable higher level operations.
'''

from __future__ import print_function
import sys
import os
import platform

StructureDelimiter = '|----'
SPACING = len(StructureDelimiter)

class DirectoryTreeNode(object):
    def __init__(self, name, size=0):
        self.size = size
        self.name = name
        self.children = set()
        self.parent = None

    def __eq__(self, other):
        if other == None:
            return False
        return self.name == other.name

    def __ne__(self, other):
        return not self.__eq__(other)

    def addChild(self,child_node):
        self.children.add(child_node)
        child_node.parent = self
        return child_node

    def getChildren(self):
        return list(self.children)

    def getParent(self):
        return self.parent

    def getSize(self):
        return self.size

    def setSize(self,size):
        self.size = size

    def getName(self):
        return self.name

    def __str__(self):
        return '\nNode: ' + self.name + ', size: ' + self.size

    # This function searches the tree starting
    # at this node, for a node with the name supplied.
    # It recursivly seaches the tree by traversing the children nodes.
    # The first node matching the targetName is returned.
    # If the search does not find a matching node None is returned.
    def find(self,targetName):
        if self.name == targetName:
            return self
        childrenList = self.getChildren()
        for child in childrenList:
            foundNode = child.find(targetName)
            if foundNode != None:
                return foundNode
        return None

    # seach the children of this node
    # for a node with the targetName name
    # If a matching node is not found return None
    def findInChildren(self,targetName):
        childrenList = self.getChildren()
        for child in childrenList:
            if targetName == child.getName():
                return child
        return None

    # Print the tree starting at this node.
    # This function indents each succesive child
    # by one additional level.
    def printTree(self,indentations=0):
        global SPACING
        formatString = '{:75s} [{:>6s}]'
        s = DirectoryTreeNode.getIndentationPrefix(indentations)
        s = s + self.getName()

        print (formatString.format(s,DirectoryTreeNode.intToSize(self.getSize())))
        childrenList = self.getChildren()
        for child in childrenList:
            child.printTree(indentations + 1)
        return

    # Converts bytes into human-readable form, ex TB/GB/MB/KB/Bytes based on size.
    @classmethod
    def intToSize(cls,size, suffix='Bytes'):
        size = float(size)
        for unit in ['Bytes','KB','MB','GB','TB']:
            if abs(size) < 1000:
                return '%3.2f %s' % (size, unit)
            if unit != 'TB':
                size /= 1000
        return '%.2f %s' % (size, 'TB')

    #
    #  find this directory path in the tree.
    #  if found return the node in the tree
    #  that represents the end segment of this path
    # i.e.  path is A/B/C/F1 return the F1 node
    # else return None for not found
    pathString = ''

    @classmethod
    def findPathInTree(cls,root,path):
        parts = path.split('/')
        currentNode = root
        global pathString
        pathString = ''
        # for each part in the path find the node in the children of current node

        for part in parts:
            node = currentNode.findInChildren(part)
            if node == None:
                return False
            pathString = pathString + '/' + node.getName()
            currentNode = node
        return True

    # return a string that is composed of spaces
    # and the StructureDelimiter string which
    # indicates the parent directory.
    @classmethod
    def getIndentationPrefix(cls,indentations):
        if indentations <= 0:
            return ''
        if indentations == 1:
            return StructureDelimiter
        max = indentations - 1
        s = ''
        for n in range(max * SPACING):
            s = s + ' '
        s = s + StructureDelimiter
        return s

    # This function is currently only used by main which
    # which is a unit test.
    # This takes a collection of directory paths
    # and using buildTree makes a tree for each path
    # in filePaths.
    @classmethod
    def buildPathTree(cls,filePaths):
        global pathString
        global root
        for path in filePaths:
            print(path)
            found = DirectoryTreeNode.findPathInTree(root, path)
            if not found:
                DirectoryTreeNode.buildTree(root,path, root)

    # build a tree for this path with
    # each succesive element a child of the
    # previous node. element 1 of the path
    # must be a child of the root
    @classmethod
    def buildTree(cls,root,path, size):
        parts = path.split('/')
        lastNode = root
        parent = root
        for part in parts:
            child = parent.findInChildren(part)
            if child == None:
                child = DirectoryTreeNode(part, size)
                parent.addChild(child)
            lastNode = parent = child
        lastNode.setSize(size)
